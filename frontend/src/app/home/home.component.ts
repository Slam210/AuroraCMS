import { CommonModule } from "@angular/common";
import { Component, OnInit } from "@angular/core";
import { Apollo, gql } from "apollo-angular";
import { HttpClientModule } from "@angular/common/http";
import { GraphQLModule } from "../graphql.module";

const TEST_QUERY = gql`
  query {
    posts(first: 5) {
      nodes {
        id
        title
        date
      }
    }
  }
`;

@Component({
  selector: "app-home",
  standalone: true,
  imports: [CommonModule, HttpClientModule],
  templateUrl: "./home.component.html",
  styleUrls: ["./home.component.scss"],
  providers: [GraphQLModule],
})
export class HomeComponent implements OnInit {
  posts: any[] = [];
  loading = true;
  error: any;

  constructor(private apollo: Apollo) {}

  ngOnInit(): void {
    this.apollo.watchQuery({ query: TEST_QUERY }).valueChanges.subscribe({
      next: (result: any) => {
        this.posts = result?.data?.posts?.nodes;
        this.loading = result.loading;
      },
      error: (err) => {
        this.error = err;
        this.loading = false;
      },
    });
  }
}
