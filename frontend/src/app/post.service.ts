import { Injectable } from "@angular/core";
import { Apollo, gql } from "apollo-angular";

@Injectable({ providedIn: "root" })
export class PostService {
  constructor(private apollo: Apollo) {}

  getPosts() {
    return this.apollo.watchQuery({
      query: gql`
        {
          posts {
            nodes {
              id
              title
              slug
              date
              content
              featuredImage {
                node {
                  sourceUrl
                }
              }
            }
          }
        }
      `,
    }).valueChanges;
  }
}
